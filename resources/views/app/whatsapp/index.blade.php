@extends('layouts.app', [
    'title' => 'WhatsApp Gateway - ' . $business->name,
    'headerTitle' => 'WhatsApp Gateway & Otomasi',
    'headerSubtitle' => 'Hubungkan nomor WhatsApp bisnis Anda untuk kirim struk digital POS & blast promosi pelanggan',
])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="waGateway()" x-init="init()">

        <!-- ========================================== -->
        <!-- 0. BREADCRUMB BAR (APPLE MINIMALIST)       -->
        <!-- ========================================== -->
        <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 py-0.5 whitespace-nowrap print:hidden"
            aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors font-medium">Dashboard</a>
            <span>›</span>
            <span class="text-black/60 dark:text-white/60 font-medium">Komunikasi &amp; Otomasi</span>
            <span>›</span>
            <span class="text-black/80 dark:text-white/80 font-medium">WhatsApp Gateway</span>
        </nav>

        <!-- ===================================================== -->
        <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
        <!-- ===================================================== -->
        <header
            class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5 transition-colors shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
            <div class="space-y-1.5 max-w-2xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1.5 bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                        <span>Multi-Device Gateway</span>
                    </span>
                    <span
                        class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1.5 bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span>
                        <span>Otomasi POS &amp; Broadcast</span>
                    </span>
                </div>
                <h1 class="text-[20px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight">
                    WhatsApp Gateway &amp; Otomasi Bisnis
                </h1>
                <p class="text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                    Sinkronisasikan nomor WhatsApp <strong
                        class="text-black dark:text-white font-medium">{{ $business->name }}</strong> untuk pengiriman struk
                    kasir tanpa kertas dan distribusi blast promosi ke pelanggan setia.
                </p>
            </div>

            <div class="flex items-center gap-2 w-full lg:w-auto">
                @if (\App\Support\Context::hasPermission('pos.terminal'))
                    <a href="{{ route('pos.terminal') }}"
                        class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 w-full sm:w-auto">
                        <i data-lucide="calculator" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>Buka Terminal Kasir POS</span>
                    </a>
                @endif
            </div>
        </header>

        <!-- ===================================================== -->
        <!-- 2. MODULE NAVIGATION SUB-TABS (Segmented Control)     -->
        <!-- ===================================================== -->
        <div
            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-2 sm:p-2.5 flex items-center justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
            <div
                class="inline-flex p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] border border-black/5 dark:border-white/5 w-full sm:w-auto overflow-x-auto text-[13px] font-medium">
                <a href="{{ route('whatsapp.index') }}"
                    class="h-8 px-4 rounded-[9px] bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] flex items-center gap-2 whitespace-nowrap">
                    <i data-lucide="smartphone" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Koneksi Gateway</span>
                </a>
                <a href="{{ route('whatsapp.broadcast.index') }}"
                    class="h-8 px-4 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                    <i data-lucide="megaphone" class="w-4 h-4"></i>
                    <span>Blast Promosi</span>
                </a>
                <a href="{{ route('whatsapp.logs.index') }}"
                    class="h-8 px-4 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                    <i data-lucide="history" class="w-4 h-4"></i>
                    <span>Log Pesan</span>
                </a>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- 3. MAIN GATEWAY COCKPIT GRID (2 COLUMNS)              -->
        <!-- ===================================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- ========================================== -->
            <!-- LEFT COLUMN: META WHATSAPP CLOUD API INTEGRATION -->
            <!-- ========================================== -->
            <div class="lg:col-span-7 space-y-6">

                <!-- 1. META EMBEDDED SIGNUP & CLOUD API CARD -->
                <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 sm:p-7 space-y-5 transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-[16px] bg-gradient-to-br from-[#1877F2] to-[#007AFF] text-white flex items-center justify-center shrink-0 shadow-md shadow-[#1877F2]/25">
                                <i data-lucide="shield-check" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h2 class="text-[17px] font-bold text-black dark:text-white tracking-tight">Meta WhatsApp Cloud API Resmi</h2>
                                <p class="text-[12.5px] text-black/55 dark:text-white/55 mt-0.5">Integrasi Tech Provider resmi Meta untuk operasional toko Anda</p>
                            </div>
                        </div>

                        <div>
                            <span class="px-3 py-1.5 rounded-full text-[11.5px] font-bold inline-flex items-center gap-1.5"
                                :class="metaAccount?.is_active ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/55 dark:text-white/55 border border-black/10 dark:border-white/10'">
                                <span class="w-2 h-2 rounded-full" :class="metaAccount?.is_active ? 'bg-[#34C759]' : 'bg-[#FF9500]'"></span>
                                <span x-text="metaAccount?.is_active ? 'Terhubung & Aktif' : 'Belum Terhubung'"></span>
                            </span>
                        </div>
                    </div>

                    <!-- Meta Free Tier Info Callout -->
                    <div class="p-4 rounded-[16px] bg-[#007AFF]/8 border border-[#007AFF]/20 text-[12.5px] text-black/75 dark:text-white/75 space-y-1">
                        <div class="flex items-center justify-between font-bold text-[#007AFF]">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="sparkles" class="w-4 h-4"></i>
                                Kuota Gratis 1.000 Percakapan/Bulan dari Meta
                            </span>
                        </div>
                        <p class="leading-relaxed text-black/65 dark:text-white/65 text-[12px]">
                            Setiap akun WhatsApp Business resmi (WABA) mendapatkan 1.000 Service Conversation gratis per bulan langsung dari Meta tanpa risiko pemblokiran nomor.
                        </p>
                    </div>

                    <!-- EMBEDDED SIGNUP FLOW (1-CLICK ONBOARDING) -->
                    <div class="p-5 sm:p-6 rounded-[18px] bg-gradient-to-br from-[#1877F2]/10 via-white dark:via-[#1C1C1E] to-[#007AFF]/8 border border-[#1877F2]/25 space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="space-y-0.5">
                                <h3 class="text-[15px] font-bold text-black dark:text-white">Pendaftaran Mandiri (Embedded Signup)</h3>
                                <p class="text-[12px] text-black/55 dark:text-white/55">Hubungkan nomor WhatsApp toko Anda dalam 1 klik tanpa perlu membuat aplikasi Meta sendiri</p>
                            </div>
                        </div>

                        <!-- Active Connected Account Details -->
                        <template x-if="metaAccount?.is_active">
                            <div class="space-y-4 pt-1">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 rounded-[16px] bg-white/80 dark:bg-black/25 border border-black/5 dark:border-white/10 text-[12.5px]">
                                    <div>
                                        <span class="text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase">Nama Bisnis Terverifikasi</span>
                                        <p class="font-bold text-black dark:text-white text-[13.5px] mt-0.5" x-text="metaAccount.verified_name || '-'"></p>
                                    </div>
                                    <div>
                                        <span class="text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase">Nomor WhatsApp Toko</span>
                                        <p class="font-mono font-bold text-[#007AFF] text-[13.5px] mt-0.5" x-text="metaAccount.display_phone_number || '-'"></p>
                                    </div>
                                    <div>
                                        <span class="text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase">Kualitas Nomor</span>
                                        <p class="font-bold text-[#34C759] mt-0.5 flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                                            <span x-text="metaAccount.quality_rating || 'GREEN'"></span>
                                        </p>
                                    </div>
                                    <div>
                                        <span class="text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase">Kapasitas Pesan (Tier)</span>
                                        <p class="font-bold text-black dark:text-white mt-0.5" x-text="metaAccount.messaging_limit_tier || 'TIER_50'"></p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-1">
                                    <span class="text-[11.5px] text-black/50 dark:text-white/50 font-mono">
                                        WABA ID: <span x-text="metaAccount.waba_id"></span>
                                    </span>
                                    <button type="button" @click="disconnectMeta()"
                                        class="min-h-[36px] px-3.5 rounded-[10px] text-[12px] font-bold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition-all">
                                        Putuskan Hubungan
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- Connect Action (When not connected - 1-Click Method Only) -->
                        <template x-if="!metaAccount?.is_active">
                            <div class="space-y-4">
                                <p class="text-[12.5px] text-black/70 dark:text-white/70 leading-relaxed">
                                    Klik tombol di bawah untuk membuka jendela resmi Meta Facebook. Masuk dengan akun Facebook Anda, pilih atau daftarkan nomor telepon WhatsApp bisnis, dan sistem COOCA akan menghubungkannya secara otomatis.
                                </p>

                                {{-- Simple 3-Step Overview --}}
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-[11.5px]">
                                    <div class="p-3 rounded-[13px] bg-white/80 dark:bg-black/25 border border-black/5 dark:border-white/5 space-y-1">
                                        <span class="font-bold text-[#1877F2] block">1. Klik Hubungkan</span>
                                        <p class="text-black/60 dark:text-white/60">Buka popup resmi Meta</p>
                                    </div>
                                    <div class="p-3 rounded-[13px] bg-white/80 dark:bg-black/25 border border-black/5 dark:border-white/5 space-y-1">
                                        <span class="font-bold text-[#1877F2] block">2. Masuk Facebook</span>
                                        <p class="text-black/60 dark:text-white/60">Pilih akun &amp; nomor toko</p>
                                    </div>
                                    <div class="p-3 rounded-[13px] bg-white/80 dark:bg-black/25 border border-black/5 dark:border-white/5 space-y-1">
                                        <span class="font-bold text-[#34C759] block">3. Langsung Terhubung</span>
                                        <p class="text-black/60 dark:text-white/60">Siap kirim struk digital</p>
                                    </div>
                                </div>

                                @if (\App\Support\Context::hasPermission('whatsapp.manage'))
                                    <button type="button" @click="launchEmbeddedSignup()" :disabled="embeddedLoading"
                                        class="w-full min-h-[46px] rounded-[14px] bg-[#1877F2] hover:bg-[#166FE5] text-white font-bold text-[13.5px] flex items-center justify-center gap-2 shadow-md shadow-[#1877F2]/25 active:scale-[0.98] transition-all disabled:opacity-50">
                                        <i data-lucide="loader-2" x-show="embeddedLoading" class="w-4 h-4 animate-spin"></i>
                                        <i data-lucide="shield-check" x-show="!embeddedLoading" class="w-4 h-4"></i>
                                        <span x-text="embeddedLoading ? 'Menghubungkan ke Meta...' : 'Hubungkan dengan WhatsApp Resmi (1-Klik Meta)'"></span>
                                    </button>
                                @endif

                                <!-- Feedback Error -->
                                <div x-show="embeddedError" x-transition class="p-3.5 rounded-[12px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[12px] text-[#C41E17] dark:text-[#FF453A] flex items-center gap-2">
                                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                                    <span x-text="embeddedError"></span>
                                </div>

                                <!-- Feedback Success -->
                                <div x-show="embeddedSuccess" x-transition class="p-3.5 rounded-[12px] bg-[#34C759]/12 border border-[#34C759]/30 text-[12px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                                    <span x-text="embeddedSuccess"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Status Layanan WhatsApp Toko -->
                    <form action="{{ route('whatsapp.settings') }}" method="POST" class="pt-1">
                        @csrf
                        <input type="hidden" name="provider" value="meta_cloud">
                        <div class="flex items-center justify-between p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                            <div class="pr-2">
                                <p class="text-[13px] font-bold text-black dark:text-white">Status Layanan WhatsApp Toko</p>
                                <p class="text-[11.5px] text-black/50 dark:text-white/50 mt-0.5">Izinkan sistem mengirimkan struk digital kasir dan blast promosi ke pelanggan</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" name="is_active" value="1" onchange="this.form.submit()" class="sr-only peer"
                                    {{ ($waSession?->is_active ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-black/[0.12] dark:bg-white/[0.15] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]"></div>
                            </label>
                        </div>
                    </form>
                </div>

            </div>

            <!-- ========================================== -->
            <!-- RIGHT COLUMN: SETTINGS & TEST SENDER       -->
            <!-- ========================================== -->
            <div class="lg:col-span-5 space-y-6">

                <!-- 1. AUTO RECEIPT SETTINGS CARD -->
                <div
                    class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] p-5 sm:p-6 space-y-4 transition-colors">
                    <div class="flex items-center gap-3 pb-3 border-b border-black/5 dark:border-white/10">
                        <div
                            class="w-9 h-9 rounded-[8px] bg-[#30B0C7]/10 text-[#30B0C7] dark:text-[#40C8E0] flex items-center justify-center">
                            <i data-lucide="receipt" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h2 class="text-[14px] font-semibold text-black dark:text-white">Pengaturan Struk Digital POS
                            </h2>
                            <p class="text-[12px] text-black/50 dark:text-white/50">Otomasi struk belanja ke WA pelanggan
                                saat checkout</p>
                        </div>
                    </div>

                    <form action="{{ route('whatsapp.settings') }}" method="POST" class="space-y-4">
                        @csrf

                        <!-- Toggle Switch Row (Grouped Inset Style) -->
                        <div
                            class="flex items-center justify-between p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                            <div class="pr-2">
                                <p class="text-[13px] font-semibold text-black dark:text-white">Auto-Kirim Struk Checkout
                                </p>
                                <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">Kirim struk otomatis jika
                                    data pelanggan memiliki nomor HP</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" name="auto_send_receipt" value="1" class="sr-only peer"
                                    {{ $waSession && $waSession->auto_send_receipt ? 'checked' : '' }}>
                                <div
                                    class="w-11 h-6 bg-black/[0.12] dark:bg-white/[0.15] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]">
                                </div>
                            </label>
                        </div>

                        <!-- Receipt Footer Note -->
                        <div class="space-y-1.5">
                            <label class="block text-[12px] font-medium text-black/70 dark:text-white/70">
                                Catatan Kaki Struk (Opsional)
                            </label>
                            <textarea name="receipt_template" rows="2"
                                placeholder="Contoh: Terima kasih sudah berbelanja! Follow IG kami @tokoukm"
                                class="w-full bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[10px] px-3.5 py-2.5 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 resize-none placeholder:text-black/35 dark:placeholder:text-white/35 transition-colors">{{ $waSession?->receipt_template ?? '' }}</textarea>
                        </div>

                        @if (\App\Support\Context::hasPermission('whatsapp.manage'))
                            <button type="submit"
                                class="w-full h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center">
                                Simpan Pengaturan Struk
                            </button>
                        @endif
                    </form>
                </div>

                <!-- 2. TEST SEND CONSOLE -->
                <div
                    class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] p-5 sm:p-6 space-y-4 transition-colors">
                    <div class="flex items-center gap-3 pb-3 border-b border-black/5 dark:border-white/10">
                        <div
                            class="w-9 h-9 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                            <i data-lucide="send" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h2 class="text-[14px] font-semibold text-black dark:text-white">Uji Coba Kirim Pesan</h2>
                            <p class="text-[12px] text-black/50 dark:text-white/50">Pastikan bot WhatsApp berfungsi lancar
                                ke nomor tujuan</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="space-y-1.5">
                            <label class="block text-[12px] font-medium text-black/70 dark:text-white/70">Nomor HP
                                Tujuan</label>
                            <input x-model="testPhone" type="tel" placeholder="08xxxxxxxxxx / 628xxxxxxxxxx"
                                class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[10px] px-3.5 text-[13px] font-semibold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 placeholder:text-black/35 dark:placeholder:text-white/35 transition-colors">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-[12px] font-medium text-black/70 dark:text-white/70">Isi Pesan
                                Tes</label>
                            <input x-model="testMessage" type="text" placeholder="Halo dari COOCA! Terima kasih sudah menghubungi kami."
                                class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[10px] px-3.5 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 placeholder:text-black/35 dark:placeholder:text-white/35 transition-colors">
                        </div>

                        @if (\App\Support\Context::hasPermission('whatsapp.manage'))
                            <button type="button" @click="sendTest()"
                                class="w-full h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 shadow-[0_1px_2px_rgba(0,122,255,0.25)] transition-all flex items-center justify-center gap-1.5"
                                :disabled="testLoading">
                                <i data-lucide="loader-2" x-show="testLoading" class="w-3.5 h-3.5 animate-spin"></i>
                                <i data-lucide="send" x-show="!testLoading" class="w-3.5 h-3.5"></i>
                                <span x-text="testLoading ? 'Sedang Mengirim...' : 'Kirim Pesan Tes Sekarang'"></span>
                            </button>
                        @endif

                        <div x-show="testResult" x-text="testResult"
                            class="p-2.5 rounded-[10px] text-[12px] text-center font-medium transition-all"
                            :class="testOk ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' :
                                'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]'">
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function waGateway() {
            return {
                provider: 'meta_cloud',
                isActive: {{ ($whatsAppAccount && $whatsAppAccount->isActive()) ? 'true' : 'false' }},
                isMetaConfigured: {{ $whatsAppAccount ? 'true' : 'false' }},
                status: '{{ $whatsAppAccount && $whatsAppAccount->isConnected() ? 'connected' : 'disconnected' }}',
                phone: '{{ $whatsAppAccount?->display_phone_number ?? '' }}',
                isLoading: false,
                testPhone: '',
                testMessage: 'Halo dari {{ addslashes($business->name) }}! Terima kasih sudah menjadi pelanggan setia kami.',
                testLoading: false,
                testResult: '',
                testOk: false,
                embeddedLoading: false,
                embeddedError: null,
                embeddedSuccess: null,
                metaEmbeddedPhoneId: null,
                metaEmbeddedWabaId: null,
                @php
                    $metaAccountData = $whatsAppAccount ? [
                        'id'                   => $whatsAppAccount->id,
                        'verified_name'        => $whatsAppAccount->verified_name,
                        'display_phone_number' => $whatsAppAccount->display_phone_number,
                        'waba_id'              => $whatsAppAccount->waba_id,
                        'phone_number_id'      => $whatsAppAccount->phone_number_id,
                        'quality_rating'       => $whatsAppAccount->quality_rating,
                        'messaging_limit_tier' => $whatsAppAccount->messaging_limit_tier,
                        'is_active'            => $whatsAppAccount->isActive(),
                    ] : null;
                @endphp
                metaAccount: @json($metaAccountData),

                init() {
                    // Dengarkan event sessionInfoListener dari Meta Embedded Signup pop-up
                    window.addEventListener('message', (event) => {
                        if (event.origin !== "https://www.facebook.com" && event.origin !== "https://web.facebook.com") {
                            return;
                        }
                        try {
                            const data = typeof event.data === 'string' ? JSON.parse(event.data) : event.data;
                            if (data && data.type === 'WA_EMBEDDED_SIGNUP') {
                                if (data.event === 'FINISH' && data.data) {
                                    this.metaEmbeddedPhoneId = data.data.phone_number_id || null;
                                    this.metaEmbeddedWabaId = data.data.waba_id || null;
                                }
                            }
                        } catch (e) {}
                    });
                },

                async launchEmbeddedSignup() {
                    this.embeddedLoading = true;
                    this.embeddedError = null;
                    this.embeddedSuccess = null;

                    try {
                        // 1. Ambil konfigurasi App ID & Config ID dari server COOCA
                        const configRes = await fetch('{{ route('whatsapp.meta.config') }}', {
                            headers: { 'Accept': 'application/json' }
                        });
                        const configData = await configRes.json();
                        if (!configRes.ok || !configData.success) {
                            this.embeddedError = configData.error || 'Konfigurasi Meta App ID belum tersedia di server.';
                            this.embeddedLoading = false;
                            return;
                        }

                        // 2. Pastikan Facebook JavaScript SDK ter-load
                        await this.ensureFbSdkLoaded(configData.app_id, configData.version || 'v21.0');

                        // 3. Launch FB.login dengan WhatsApp Embedded Signup
                        const loginOptions = {
                            response_type: 'code',
                            override_default_response_type: true,
                            extras: {
                                setup: {
                                    business: {
                                        name: '{{ addslashes($business->name) }}'
                                    }
                                }
                            }
                        };
                        if (configData.config_id) {
                            loginOptions.config_id = configData.config_id;
                        }

                        FB.login((response) => {
                            if (response.authResponse && response.authResponse.code) {
                                this.exchangeEmbeddedCode(
                                    response.authResponse.code,
                                    this.metaEmbeddedWabaId,
                                    this.metaEmbeddedPhoneId
                                );
                            } else {
                                this.embeddedLoading = false;
                                this.embeddedError = 'Otorisasi Meta dibatalkan atau tidak diselesaikan.';
                            }
                        }, loginOptions);
                    } catch (err) {
                        this.embeddedLoading = false;
                        this.embeddedError = 'Gagal membuka jendela Meta Embedded Signup: ' + (err.message || err);
                    }
                },

                async exchangeEmbeddedCode(code, wabaId = null, phoneId = null) {
                    this.embeddedLoading = true;
                    try {
                        const res = await fetch('{{ route('whatsapp.meta.exchange-code') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                            },
                            body: JSON.stringify({
                                code: code,
                                waba_id: wabaId,
                                phone_number_id: phoneId
                            })
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.embeddedSuccess = 'WhatsApp resmi Meta berhasil terhubung!';
                            setTimeout(() => window.location.reload(), 1200);
                        } else {
                            this.embeddedError = data.error || 'Gagal menukarkan token otorisasi Meta.';
                        }
                    } catch (e) {
                        this.embeddedError = 'Kesalahan koneksi saat menyimpan kredensial Meta.';
                    } finally {
                        this.embeddedLoading = false;
                    }
                },

                ensureFbSdkLoaded(appId, version) {
                    return new Promise((resolve) => {
                        if (window.FB) {
                            resolve();
                            return;
                        }
                        window.fbAsyncInit = function() {
                            FB.init({
                                appId: appId,
                                autoLogAppEvents: true,
                                xfbml: true,
                                version: version
                            });
                            resolve();
                        };
                        const js = document.createElement('script');
                        js.id = 'facebook-jssdk';
                        js.src = 'https://connect.facebook.net/en_US/sdk.js';
                        js.async = true;
                        js.defer = true;
                        document.body.appendChild(js);
                    });
                },

                async disconnectMeta() {
                    let confirmed = false;
                    if (window.AppAlert) {
                        confirmed = await AppAlert.confirm({
                            title: 'Putus Koneksi WhatsApp Meta?',
                            message: 'Yakin ingin menonaktifkan integrasi WhatsApp Cloud API resmi? Pengiriman struk otomatis akan berhenti.',
                            type: 'danger',
                            confirmText: 'Ya, Putuskan',
                            cancelText: 'Batal'
                        });
                    } else {
                        confirmed = confirm('Yakin ingin menonaktifkan integrasi WhatsApp Cloud API resmi?');
                    }
                    if (!confirmed) return;

                    try {
                        const res = await fetch('{{ route('whatsapp.meta.disconnect') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            window.location.reload();
                        }
                    } catch (e) {}
                },

                async sendTest() {
                    if (!this.testPhone.trim() || !this.testMessage.trim()) {
                        this.testResult = 'Mohon isi nomor tujuan dan pesan terlebih dahulu.';
                        this.testOk = false;
                        return;
                    }
                    this.testLoading = true;
                    this.testResult = '';
                    try {
                        const res = await fetch('{{ route('whatsapp.test') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                            },
                            body: JSON.stringify({
                                phone: this.testPhone,
                                message: this.testMessage
                            })
                        });
                        const data = await res.json();
                        this.testOk = data.success ?? false;
                        this.testResult = this.testOk ? 'Pesan tes WhatsApp berhasil terkirim!' : ('Gagal: ' + (data
                            .error || 'Server error'));
                    } catch (e) {
                        this.testResult = 'Kesalahan jaringan saat mengirim pesan.';
                        this.testOk = false;
                    } finally {
                        this.testLoading = false;
                    }
                }
            };
        }
    </script>
@endpush
