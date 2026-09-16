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
            <!-- LEFT COLUMN: PROVIDER SELECTOR & CONNECTION -->
            <!-- ========================================== -->
            <div class="lg:col-span-7 space-y-6">

                <!-- 1. PROVIDER SWITCHER & STATUS TOGGLE CARD -->
                <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] p-5 sm:p-6 space-y-5 transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[12px] bg-gradient-to-br from-[#007AFF] to-[#5856D6] text-white flex items-center justify-center shrink-0 shadow-sm shadow-[#007AFF]/20">
                                <i data-lucide="layers" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h2 class="text-[15px] font-bold text-black dark:text-white">Pilihan Gateway WhatsApp</h2>
                                <p class="text-[12px] text-black/50 dark:text-white/50">Pilih penyedia jalur komunikasi yang sesuai dengan skala bisnis</p>
                            </div>
                        </div>

                        <!-- Provider Switcher Buttons (Apple Segmented Style) -->
                        <div class="p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] inline-flex items-center gap-1 text-[12px] font-medium">
                            <button type="button" @click="provider = 'meta_cloud'"
                                :class="provider === 'meta_cloud' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 hover:text-black'"
                                class="px-3.5 py-1.5 rounded-[9px] transition-all flex items-center gap-1.5">
                                <i data-lucide="cloud" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Meta Cloud API</span>
                            </button>
                            <button type="button" @click="provider = 'baileys'"
                                :class="provider === 'baileys' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 hover:text-black'"
                                class="px-3.5 py-1.5 rounded-[9px] transition-all flex items-center gap-1.5">
                                <i data-lucide="qr-code" class="w-3.5 h-3.5 text-[#25D366]"></i>
                                <span>Scan QR (Baileys)</span>
                            </button>
                        </div>
                    </div>

                    <!-- Provider Details & Settings Form -->
                    <form action="{{ route('whatsapp.settings') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="provider" :value="provider">

                        <!-- Active Toggle Row -->
                        <div class="flex items-center justify-between p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                            <div class="pr-2">
                                <p class="text-[13px] font-semibold text-black dark:text-white">Status Layanan WhatsApp Bisnis</p>
                                <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">Aktifkan untuk mengizinkan sistem mengirim struk dan broadcast otomatis</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                                    {{ ($waSession?->is_active ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-black/[0.12] dark:bg-white/[0.15] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]"></div>
                            </label>
                        </div>

                        <!-- META CLOUD API CONFIGURATION (Shown when meta_cloud selected) -->
                        <div x-show="provider === 'meta_cloud'" x-transition class="space-y-4 pt-1">
                            <div class="p-4 rounded-[14px] bg-[#007AFF]/8 border border-[#007AFF]/20 text-[12px] text-black/75 dark:text-white/75 space-y-1.5">
                                <div class="flex items-center justify-between font-bold text-[#007AFF]">
                                    <span class="flex items-center gap-1.5">
                                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                                        Jalur Resmi Meta Facebook (Bebas Pemblokiran)
                                    </span>
                                    <a href="https://developers.facebook.com/apps/" target="_blank" rel="noopener noreferrer" class="hover:underline flex items-center gap-1">
                                        <span>Meta Portal</span>
                                        <i data-lucide="external-link" class="w-3 h-3"></i>
                                    </a>
                                </div>
                                <p class="leading-relaxed">
                                    Gunakan Meta WhatsApp Cloud API langsung dengan kuota gratis <strong>1.000 percakapan per bulan</strong> langsung dari Facebook tanpa memerlukan server QR berjalan lokal.
                                </p>
                            </div>

                            {{-- PANDUAN LENGKAP STEP-BY-STEP SETUP META CLOUD API --}}
                            @include('partials.whatsapp-meta-setup-guide', ['mode' => 'owner'])

                            <div class="space-y-3">
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55">Permanent Access Token</label>
                                        <button type="button" @click="showMetaToken = !showMetaToken" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                            <span x-text="showMetaToken ? 'Sembunyikan' : 'Tampilkan Token'"></span>
                                        </button>
                                    </div>
                                    <div class="relative">
                                        <input :type="showMetaToken ? 'text' : 'password'" name="meta_access_token" x-model="metaToken" placeholder="EAAG... (System User Token Meta)"
                                            class="w-full bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[10px] px-3.5 pr-10 py-2.5 text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                                        <button type="button" @click="showMetaToken = !showMetaToken" class="absolute right-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">
                                            <i data-lucide="eye" x-show="!showMetaToken" class="w-4 h-4"></i>
                                            <i data-lucide="eye-off" x-show="showMetaToken" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1">Phone Number ID</label>
                                        <input type="text" name="meta_phone_number_id" x-model="metaPhoneId" placeholder="Contoh: 104523984712398"
                                            class="w-full bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[10px] px-3.5 py-2.5 text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1">WABA Account ID</label>
                                        <input type="text" name="meta_waba_id" x-model="metaWabaId" placeholder="Contoh: 109283746501928"
                                            class="w-full bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[10px] px-3.5 py-2.5 text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                                    </div>
                                </div>

                                {{-- Verification Action Row --}}
                                <div class="pt-1">
                                    <div class="flex items-center justify-between p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5">
                                        <div class="text-[12px] text-black/70 dark:text-white/70">
                                            <span>Pastikan kredensial aktif sebelum menyimpan</span>
                                        </div>
                                        <button type="button" @click="verifyMetaCredentials()" :disabled="metaVerifyLoading"
                                            class="min-h-[34px] px-3.5 rounded-[8px] text-[11.5px] font-bold bg-[#007AFF]/12 hover:bg-[#007AFF]/20 text-[#007AFF] active:scale-[0.98] transition-all inline-flex items-center gap-1.5 disabled:opacity-50">
                                            <i data-lucide="loader-2" x-show="metaVerifyLoading" class="w-3.5 h-3.5 animate-spin"></i>
                                            <i data-lucide="shield-check" x-show="!metaVerifyLoading" class="w-3.5 h-3.5"></i>
                                            <span x-text="metaVerifyLoading ? 'Memeriksa...' : 'Uji Kredensial Meta'"></span>
                                        </button>
                                    </div>

                                    {{-- Live Verification Success --}}
                                    <div x-show="metaVerifyResult" x-transition class="mt-2.5 p-3 rounded-[12px] bg-[#34C759]/12 border border-[#34C759]/30 text-[12px] text-[#248A3D] dark:text-[#30D158] flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                                            <span><strong>Terverifikasi:</strong> <span x-text="metaVerifyResult?.verified_name || 'Akun Meta Valid'"></span> (<span class="font-mono" x-text="metaVerifyResult?.display_phone_number || metaPhoneId"></span>)</span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/20 shrink-0" x-text="'Kualitas: ' + (metaVerifyResult?.quality_rating || 'GREEN')"></span>
                                    </div>

                                    {{-- Live Verification Error --}}
                                    <div x-show="metaVerifyError" x-transition class="mt-2.5 p-3 rounded-[12px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[12px] text-[#C41E17] dark:text-[#FF453A] flex items-start gap-2">
                                        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 mt-0.5"></i>
                                        <div x-text="metaVerifyError"></div>
                                    </div>
                                </div>
                            </div>

                            @if (\App\Support\Context::hasPermission('whatsapp.manage'))
                                <button type="submit"
                                    class="w-full h-11 rounded-[12px] text-[13.5px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 shadow-[0_4px_14px_rgba(0,122,255,0.28)]">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    <span>Simpan Pengaturan Meta Cloud API</span>
                                </button>
                            @endif
                        </div>

                        <!-- BAILEYS QR NOTICE (Shown when baileys selected) -->
                        <div x-show="provider === 'baileys'" x-transition class="space-y-4 pt-1">
                            {{-- Apple HIG Ban Warning Banner --}}
                            <div class="p-4 sm:p-4.5 rounded-[14px] bg-[#FF9500]/12 border border-[#FF9500]/30 text-[12.5px] space-y-2">
                                <div class="flex items-center gap-2 font-bold text-[#B25E00] dark:text-[#FF9F0A]">
                                    <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0"></i>
                                    <span>⚠️ Peringatan Tingkat Blokir Sangat Besar</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A] font-bold ml-auto">Risiko Tinggi</span>
                                </div>
                                <p class="text-black/75 dark:text-white/75 leading-relaxed">
                                    Penggunaan sesi WhatsApp melalui Scan QR (Baileys) dapat memicu <strong>pemblokiran permanen oleh pihak Meta/WhatsApp</strong> jika nomor sering digunakan mengirim blast promosi atau pesan otomatis. Gunakan nomor khusus atau beralih ke Meta Cloud API untuk proteksi penuh.
                                </p>
                            </div>

                            @if (\App\Support\Context::hasPermission('whatsapp.manage'))
                                <button type="submit"
                                    class="w-full h-10 rounded-[10px] text-[13px] font-bold text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    <span>Tetapkan Baileys Sebagai Provider</span>
                                </button>
                            @endif
                        </div>
                    </form>
                </div>

                <!-- 2. BAILEYS SCAN QR & CONNECTION CARD (Shown when provider === 'baileys') -->
                <div x-show="provider === 'baileys'"
                    class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] p-5 sm:p-6 space-y-5 transition-colors">

                    <!-- Card Header with Real-Time Status Badge -->
                    <div class="flex items-center justify-between pb-4 border-b border-black/5 dark:border-white/10">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                                <i data-lucide="qr-code" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h2 class="text-[15px] font-semibold text-black dark:text-white">Status Koneksi Scan QR (Baileys)</h2>
                                <p class="text-[12px] text-black/50 dark:text-white/50">Sinkronisasi nomor HP bisnis ke server scan barcode</p>
                            </div>
                        </div>

                        <div>
                            <!-- Connected Badge -->
                            <span x-show="status === 'connected'"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] animate-pulse"></span>
                                <span>Terhubung</span>
                            </span>
                            <!-- Scan QR Badge -->
                            <span x-show="status === 'scan_qr'"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500] animate-pulse"></span>
                                <span>Menunggu Scan</span>
                            </span>
                            <!-- Disconnected Badge -->
                            <span x-show="status === 'disconnected' || status === ''"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-black/6 dark:bg-white/8 text-black/60 dark:text-white/60">
                                <span class="w-1.5 h-1.5 rounded-full bg-black/30 dark:bg-white/30"></span>
                                <span>Tidak Terhubung</span>
                            </span>
                        </div>
                    </div>

                    <!-- 1. CONNECTED STATE (Apple HIG Callout Card) -->
                    <div x-show="status === 'connected'" x-transition.opacity class="space-y-4">
                        <div
                            class="p-5 rounded-[14px] bg-[#34C759]/8 dark:bg-[#34C759]/10 border border-[#34C759]/20 flex items-center gap-4">
                            <div
                                class="w-12 h-12 rounded-[12px] bg-[#34C759]/15 border border-[#34C759]/30 flex items-center justify-center shrink-0 text-[#34C759] dark:text-[#30D158]">
                                <i data-lucide="check-circle" class="w-6 h-6"></i>
                            </div>
                            <div class="space-y-0.5">
                                <div
                                    class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wide">
                                    Nomor WhatsApp Aktif</div>
                                <div class="text-[20px] font-bold text-black dark:text-white tabular-nums tracking-tight"
                                    x-text="phone ? '+' + phone : 'Sesi Aktif Terhubung'"></div>
                                <div class="text-[12px] text-black/60 dark:text-white/60 flex items-center gap-1.5 pt-0.5">
                                    <i data-lucide="smartphone" class="w-3.5 h-3.5 text-black/40 dark:text-white/40"></i>
                                    <span
                                        x-text="deviceName ? 'Perangkat: ' + deviceName : 'WhatsApp Web Multi-Device'"></span>
                                </div>
                            </div>
                        </div>

                        @if (\App\Support\Context::hasPermission('whatsapp.manage'))
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                                <a href="{{ route('whatsapp.broadcast.create') }}"
                                    class="h-10 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                                    <i data-lucide="megaphone" class="w-4 h-4"></i>
                                    <span>Buat Blast Promosi</span>
                                </a>
                                <button type="button" @click="disconnectWa()"
                                    class="h-10 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5"
                                    :disabled="isLoading">
                                    <i data-lucide="log-out" class="w-4 h-4"></i>
                                    <span>Putus Koneksi Sesi</span>
                                </button>
                            </div>
                        @endif
                    </div>

                    <!-- 2. SCAN QR CODE STATE -->
                    <div x-show="status === 'scan_qr'" x-transition.opacity class="text-center space-y-5 py-2">
                        <div>
                            <h3 class="text-[15px] font-semibold text-black dark:text-white">Pindai Kode QR dengan WhatsApp
                            </h3>
                            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Arahkan kamera WhatsApp ponsel
                                Anda ke kode QR di bawah ini</p>
                        </div>

                        <!-- Centered High-Contrast QR Code Card -->
                        <div class="flex justify-center">
                            <div class="relative inline-block">
                                <!-- Loading Skeleton -->
                                <div x-show="!qrDataUrl"
                                    class="w-56 h-56 rounded-[16px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 flex flex-col items-center justify-center gap-3">
                                    <div
                                        class="w-8 h-8 border-3 border-black/10 dark:border-white/10 border-t-[#007AFF] rounded-full animate-spin">
                                    </div>
                                    <span class="text-[12px] text-black/50 dark:text-white/50 font-medium">Menghasilkan QR
                                        Code...</span>
                                </div>
                                <!-- Actual QR Image -->
                                <div x-show="qrDataUrl"
                                    class="bg-white p-4 rounded-[18px] shadow-[0_12px_32px_rgba(0,0,0,0.12)] border border-black/10 inline-block transition-transform">
                                    <img :src="qrDataUrl" alt="WhatsApp QR Code"
                                        class="w-48 h-48 rounded-[10px] block">
                                </div>
                            </div>
                        </div>

                        <!-- Refresh QR Button -->
                        @if (\App\Support\Context::hasPermission('whatsapp.manage'))
                            <div class="flex items-center justify-center gap-2 pt-0.5">
                                <button type="button" @click="fetchQr()"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-[8px] text-[12px] font-medium bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 text-black/70 dark:text-white/70 transition-all cursor-pointer">
                                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                                    <span>Muat Ulang Barcode QR</span>
                                </button>
                            </div>
                        @endif

                        <!-- Steps Guide (Grouped Inset Style) -->
                        <div
                            class="text-left bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 rounded-[12px] p-4 space-y-2.5 text-[12px] text-black/70 dark:text-white/70">
                            <p class="font-semibold text-black dark:text-white flex items-center gap-1.5">
                                <i data-lucide="info" class="w-4 h-4 text-[#007AFF]"></i>
                                <span>Panduan Menghubungkan:</span>
                            </p>
                            <div class="flex items-start gap-2.5">
                                <span
                                    class="w-5 h-5 rounded-full bg-black/[0.06] dark:bg-white/[0.08] text-black dark:text-white flex items-center justify-center text-[11px] font-semibold shrink-0 mt-0.5">1</span>
                                <span>Buka aplikasi <strong>WhatsApp</strong> di HP Anda</span>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <span
                                    class="w-5 h-5 rounded-full bg-black/[0.06] dark:bg-white/[0.08] text-black dark:text-white flex items-center justify-center text-[11px] font-semibold shrink-0 mt-0.5">2</span>
                                <span>Ketuk ikon <strong>⋮ Menu (Android)</strong> atau <strong>Pengaturan (iOS)</strong>
                                    &rarr; pilih <strong>Perangkat Tertaut</strong></span>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <span
                                    class="w-5 h-5 rounded-full bg-black/[0.06] dark:bg-white/[0.08] text-black dark:text-white flex items-center justify-center text-[11px] font-semibold shrink-0 mt-0.5">3</span>
                                <span>Ketuk <strong>Tautkan Perangkat</strong> dan arahkan kamera ke kode QR di atas</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-center gap-2 text-[12px] font-medium text-[#007AFF]">
                            <div class="w-2 h-2 rounded-full bg-[#007AFF] animate-ping"></div>
                            <span>Sistem otomatis tersambung begitu QR berhasil dipindai...</span>
                        </div>
                    </div>

                    <!-- 3. DISCONNECTED STATE (Apple Empty State Style) -->
                    <div x-show="status === 'disconnected' || status === ''" x-transition.opacity
                        class="text-center py-8 space-y-4">
                        <div
                            class="w-16 h-16 rounded-[16px] bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center mx-auto text-black/30 dark:text-white/30 border border-black/5 dark:border-white/10">
                            <i data-lucide="smartphone-nfc" class="w-8 h-8"></i>
                        </div>
                        <div>
                            <h3 class="text-black dark:text-white font-semibold text-[15px]">WhatsApp Belum Terhubung</h3>
                            <p class="text-black/50 dark:text-white/50 text-[13px] mt-1 max-w-sm mx-auto leading-relaxed">
                                Mulai sesi baru untuk memindai kode QR dan mengaktifkan pengiriman struk digital kasir &amp;
                                blast promosi.
                            </p>
                        </div>
                        @if (\App\Support\Context::hasPermission('whatsapp.manage'))
                            <button type="button" @click="startSession()"
                                class="h-11 px-6 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 shadow-[0_1px_2px_rgba(0,122,255,0.25)] inline-flex items-center gap-2 transition-all cursor-pointer"
                                :disabled="isLoading">
                                <i data-lucide="loader-2" x-show="isLoading" class="w-4 h-4 animate-spin"></i>
                                <i data-lucide="qr-code" x-show="!isLoading" class="w-4 h-4"></i>
                                <span x-text="isLoading ? 'Menghubungkan ke Server WA...' : 'Mulai Scan QR Code'"></span>
                            </button>
                        @endif
                    </div>
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
                            <input x-model="testMessage" type="text" placeholder="Halo dari COOCA! 👋"
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
                provider: '{{ $waSession?->provider ?? 'baileys' }}',
                isActive: {{ ($waSession?->is_active ?? true) ? 'true' : 'false' }},
                isMetaConfigured: {{ (!empty($waSession?->meta_access_token) && !empty($waSession?->meta_phone_number_id)) ? 'true' : 'false' }},
                status: '{{ $liveStatus ?? ($waSession?->status ?? 'disconnected') }}',
                phone: '{{ $waSession?->phone_number ?? '' }}',
                deviceName: '{{ $waSession?->device_name ?? '' }}',
                qrDataUrl: {!! json_encode($qrDataUrl ?? null) !!},
                isLoading: false,
                pollTimer: null,
                testPhone: '',
                testMessage: 'Halo dari {{ addslashes($business->name) }}! 👋 Terima kasih sudah menjadi pelanggan setia kami.',
                testLoading: false,
                testResult: '',
                testOk: false,

                metaToken: '{{ addslashes($waSession?->meta_access_token ?? '') }}',
                metaPhoneId: '{{ addslashes($waSession?->meta_phone_number_id ?? '') }}',
                metaWabaId: '{{ addslashes($waSession?->meta_waba_id ?? '') }}',
                showMetaToken: false,
                metaVerifyLoading: false,
                metaVerifyResult: null,
                metaVerifyError: null,

                async verifyMetaCredentials() {
                    if (!this.metaToken.trim() || !this.metaPhoneId.trim()) {
                        this.metaVerifyError = '⚠️ Mohon isi Access Token dan Phone Number ID terlebih dahulu.';
                        this.metaVerifyResult = null;
                        return;
                    }
                    this.metaVerifyLoading = true;
                    this.metaVerifyResult = null;
                    this.metaVerifyError = null;
                    try {
                        const res = await fetch('{{ route('whatsapp.verify-meta') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                            },
                            body: JSON.stringify({
                                token: this.metaToken.trim(),
                                phone_number_id: this.metaPhoneId.trim()
                            })
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.metaVerifyResult = data.data;
                        } else {
                            this.metaVerifyError = data.error || 'Gagal memverifikasi kredensial Meta.';
                        }
                    } catch (e) {
                        this.metaVerifyError = 'Kesalahan koneksi saat menghubungi server verifikasi.';
                    } finally {
                        this.metaVerifyLoading = false;
                    }
                },

                init() {
                    // Hanya cek status existing - TIDAK auto-start sesi baru.
                    // Polling dimulai hanya jika sesi sebelumnya masih di scan_qr,
                    // atau jika user klik tombol "Mulai Scan QR Code".
                    if (this.provider === 'baileys') {
                        this.checkStatus();
                    }
                },

                // Cek status existing sesi tanpa memulai sesi baru.
                async checkStatus() {
                    if (this.provider === 'meta_cloud') return;
                    try {
                        const res = await fetch('{{ route('whatsapp.qr') }}', {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                            }
                        });
                        const data = await res.json();
                        if (data.status) {
                            const raw = data.status.toUpperCase();
                            if (raw === 'CONNECTED') {
                                this.status = 'connected';
                                this.phone = data.phone || this.phone;
                                this.deviceName = data.deviceName || this.deviceName;
                                if (this.pollTimer) clearInterval(this.pollTimer);
                            } else if (raw === 'SCAN_QR' || data.qrDataUrl) {
                                // Sesi sudah dimulai sebelumnya dan menunggu scan
                                this.status = 'scan_qr';
                                if (data.qrDataUrl) this.qrDataUrl = data.qrDataUrl;
                                // Lanjutkan polling karena sesi ini sudah aktif
                                this.pollStatus();
                            } else {
                                // disconnected atau no session - biarkan UI tampilkan state disconnected
                                this.status = 'disconnected';
                            }
                        }
                    } catch (e) {
                        // Jika WA server tidak bisa diakses, tampilkan disconnected
                        this.status = 'disconnected';
                    }
                },

                // Fetch QR saat sedang polling (sesi sudah aktif)
                async fetchQr() {
                    try {
                        const res = await fetch('{{ route('whatsapp.qr') }}', {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                            }
                        });
                        const data = await res.json();
                        if (data.status) {
                            const raw = data.status.toUpperCase();
                            if (raw === 'CONNECTED') {
                                this.status = 'connected';
                                if (this.pollTimer) clearInterval(this.pollTimer);
                                setTimeout(() => window.location.reload(), 800);
                            } else if (raw === 'SCAN_QR' || data.qrDataUrl) {
                                this.status = 'scan_qr';
                                if (data.qrDataUrl) this.qrDataUrl = data.qrDataUrl;
                            } else {
                                this.status = 'disconnected';
                                if (this.pollTimer) clearInterval(this.pollTimer);
                            }
                        }
                    } catch (e) {}
                },

                pollStatus() {
                    if (this.pollTimer) clearInterval(this.pollTimer);
                    this.pollTimer = setInterval(() => {
                        this.fetchQr();
                    }, 2500);
                },

                async startSession() {
                    this.isLoading = true;
                    this.qrDataUrl = null;
                    try {
                        await fetch('{{ route('whatsapp.start') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                            }
                        });
                        // Set status scan_qr hanya setelah request start berhasil
                        this.status = 'scan_qr';
                        // Mulai polling untuk mendapatkan QR dan memantau scan
                        await this.fetchQr();
                        this.pollStatus();
                    } catch (e) {
                        this.status = 'disconnected';
                    } finally {
                        this.isLoading = false;
                    }
                },

                async disconnectWa() {
                    let confirmed = false;
                    if (window.AppAlert) {
                        confirmed = await AppAlert.confirm({
                            title: 'Putus Koneksi WhatsApp?',
                            message: 'Yakin ingin memutus koneksi WhatsApp bisnis Anda? Sesi QR harus dipindai ulang nanti.',
                            type: 'danger',
                            confirmText: 'Ya, Putuskan Sesi',
                            cancelText: 'Batal'
                        });
                    } else {
                        confirmed = confirm('Yakin ingin memutus koneksi WhatsApp bisnis Anda?');
                    }
                    if (!confirmed) return;
                    this.isLoading = true;
                    try {
                        await fetch('{{ route('whatsapp.disconnect') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                            }
                        });
                        this.status = 'disconnected';
                        this.phone = '';
                        this.deviceName = '';
                        this.qrDataUrl = null;
                    } catch (e) {} finally {
                        this.isLoading = false;
                    }
                },

                async sendTest() {
                    if (!this.testPhone.trim() || !this.testMessage.trim()) {
                        this.testResult = '⚠️ Mohon isi nomor tujuan dan pesan terlebih dahulu.';
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
                        this.testResult = this.testOk ? '✅ Pesan tes WhatsApp berhasil terkirim!' : ('❌ Gagal: ' + (data
                            .error || 'Server error'));
                    } catch (e) {
                        this.testResult = '❌ Kesalahan jaringan saat mengirim pesan.';
                        this.testOk = false;
                    } finally {
                        this.testLoading = false;
                    }
                }
            };
        }
    </script>
@endpush
