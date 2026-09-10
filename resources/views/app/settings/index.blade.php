@extends('layouts.app', [
    'title' => 'Pengaturan Bisnis & Template',
    'headerTitle' => 'Pengaturan Bisnis & Template Industri',
    'headerSubtitle' => 'Konfigurasi profil usaha, logo resmi, instruksi transfer faktur, strategi pembulatan HPP, dan preset industri.'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    activeTab: 'general',
    templateSearch: '',
    selectedCategory: 'all',
    logoPreview: '{{ $business->logo_url }}',
    roundingStrategy: '{{ $business->rounding_strategy ?? 'ROUND_100' }}',
    templateModalOpen: false,
    selectedTemplate: { code: '', name: '', category: '' },

    openApplyTemplate(code, name, category) {
        this.selectedTemplate = { code, name, category };
        this.templateModalOpen = true;
    },

    closeApplyTemplate() {
        this.templateModalOpen = false;
        this.selectedTemplate = { code: '', name: '', category: '' };
    },

    submitApplyTemplate() {
        if (this.selectedTemplate.code) {
            document.getElementById('form-apply-template-' + this.selectedTemplate.code).submit();
        }
    },

    handleLogoChange(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (ev) => { this.logoPreview = ev.target.result; };
            reader.readAsDataURL(file);
        }
    },

    getRoundingExample(strategy) {
        const samples = {
            'ROUND': 'Rp 14.234 → Rp 14.234 (Presisi desimal normal)',
            'CEIL': 'Rp 14.234 → Rp 15.000 (Plafon ke atas)',
            'FLOOR': 'Rp 14.234 → Rp 14.000 (Pangkas ke bawah)',
            'ROUND_50': 'Rp 14.234 → Rp 14.250 (Kelipatan 50 terdekat)',
            'ROUND_100': 'Rp 14.234 → Rp 14.200 (Kelipatan 100 terdekat)',
            'ROUND_500': 'Rp 14.234 → Rp 14.500 (Kelipatan 500 terdekat)',
            'ROUND_1000': 'Rp 14.234 → Rp 14.000 (Kelipatan 1.000 terdekat)'
        };
        return samples[strategy] || 'Standar pembulatan sistem';
    }
}">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <span class="text-black/70 dark:text-white/70 font-medium">Pengaturan</span>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Profil &amp; Template</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Pengaturan Bisnis &amp; Industri</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Profil usaha resmi, format faktur, aturan pembulatan, dan 20 preset template industri.</p>
        </div>

        <!-- Segmented Controls Switcher -->
        <div class="inline-flex p-0.5 rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] text-[13px] font-medium self-stretch sm:self-auto">
            <button type="button" @click="activeTab = 'general'"
                :class="activeTab === 'general' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55'"
                class="px-3.5 py-1.5 rounded-[7px] transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.75a1.5 1.5 0 011.5-1.5h3a1.5 1.5 0 011.5 1.5V21" />
                </svg>
                <span>Profil &amp; Pembulatan</span>
            </button>

            <button type="button" @click="activeTab = 'templates'"
                :class="activeTab === 'templates' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55'"
                class="px-3.5 py-1.5 rounded-[7px] transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0l4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0l-5.571 3-5.571-3" />
                </svg>
                <span>20 Template Industri</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-semibold bg-black/10 dark:bg-white/10 tabular-nums">{{ $templates->count() }}</span>
            </button>
        </div>
    </header>

    <!-- Flash Notifications (Apple Banner Style) -->
    @if(session('success'))
        <div class="rounded-[14px] bg-[#34C759]/12 border border-[#34C759]/20 px-4 py-3 text-[13px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-3">
            <span class="w-2 h-2 rounded-full bg-[#34C759] shrink-0"></span>
            <div class="flex-1 font-medium">{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-[14px] bg-[#FF3B30]/12 border border-[#FF3B30]/20 px-4 py-3 text-[13px] text-[#C41E17] dark:text-[#FF453A] flex items-center gap-3">
            <span class="w-2 h-2 rounded-full bg-[#FF3B30] shrink-0"></span>
            <div class="flex-1 font-medium">{{ session('error') }}</div>
        </div>
    @endif

    <!-- ===================================================== -->
    <!-- TAB 1: PROFIL BISNIS, LOGO, BANK & PEMBULATAN        -->
    <!-- ===================================================== -->
    <div x-show="activeTab === 'general'" class="space-y-6">
        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Section 1: Logo & Identitas Kop Surat -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5">
                <div class="flex items-start justify-between gap-4 border-b border-black/5 dark:border-white/5 pb-4">
                    <div>
                        <h2 class="text-[17px] font-semibold text-black dark:text-white">Identitas Perusahaan &amp; Logo Resmi</h2>
                        <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Informasi ini otomatis tampil sebagai kop surat resmi dan instruksi pembayaran pada Faktur Penjualan (Invoice) dan Purchase Order (PO).</p>
                    </div>
                    <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">
                        Kop Surat Resmi
                    </span>
                </div>

                <!-- Logo Section -->
                <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 flex flex-col sm:flex-row items-center gap-5">
                    <div class="w-24 h-24 rounded-[14px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 flex items-center justify-center overflow-hidden shrink-0 shadow-[0_1px_3px_rgba(0,0,0,0.05)]">
                        <template x-if="logoPreview">
                            <img :src="logoPreview" alt="Logo Bisnis" class="w-full h-full object-contain p-2">
                        </template>
                        <template x-if="!logoPreview">
                            <div class="text-center p-2 text-black/30 dark:text-white/30">
                                <svg class="w-8 h-8 mx-auto mb-1 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                                <span class="text-[10px] font-medium block">Belum ada logo</span>
                            </div>
                        </template>
                    </div>

                    <div class="flex-1 space-y-2 text-left w-full">
                        <label class="block text-[13px] font-semibold text-black dark:text-white">Unggah Berkas Logo Baru</label>
                        <input type="file" name="logo" id="biz_logo_input" accept="image/*" @change="handleLogoChange($event)"
                            class="block w-full text-[13px] text-black/60 dark:text-white/60 file:mr-3 file:py-1.5 file:px-3 file:rounded-[8px] file:border-0 file:text-[12px] file:font-semibold file:bg-[#007AFF] file:text-white hover:file:bg-[#0071E3] file:cursor-pointer cursor-pointer">
                        <p class="text-[12px] text-black/40 dark:text-white/40">
                            Format didukung: PNG, JPG, WEBP, SVG. Maksimal 2MB. Format transparan PNG direkomendasikan.
                        </p>

                        @if($business->logo_path)
                        <label class="inline-flex items-center gap-2 mt-1.5 cursor-pointer select-none text-[13px] text-[#FF3B30] font-medium">
                            <input type="checkbox" name="remove_logo" value="1" class="rounded-[4px] border-black/20 text-[#FF3B30] focus:ring-[#FF3B30]">
                            <span>Hapus logo saat ini</span>
                        </label>
                        @endif
                    </div>
                </div>

                <!-- Input Grid: Nama & NPWP -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            Nama Usaha / Perusahaan <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="text" name="name" value="{{ $business->name }}" required
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            NPWP / Identitas Pajak
                        </label>
                        <input type="text" name="tax_identification_number" value="{{ $business->tax_identification_number }}" placeholder="01.234.567.8-901.000"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white tabular-nums placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>

                <!-- Input Grid: Phone & Email -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            No. Telepon / WhatsApp Resmi
                        </label>
                        <input type="text" name="phone" value="{{ $business->phone }}" placeholder="0812-3456-7890"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white tabular-nums placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            Email Penagihan &amp; Operasional
                        </label>
                        <input type="email" name="email" value="{{ $business->email }}" placeholder="finance@perusahaan.com"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>

                <!-- Address -->
                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                        Alamat Kantor / Workshop (Kop Surat)
                    </label>
                    <textarea name="address" rows="2" placeholder="Jl. Sudirman No. 45, Gedung Cyber Lt. 5, Jakarta..."
                        class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-3 text-[15px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition resize-none">{{ $business->address }}</textarea>
                </div>
            </div>

            <!-- Section 2: Konfigurasi POS & Pajak -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
                <div class="border-b border-black/5 dark:border-white/5 pb-3">
                    <h2 class="text-[17px] font-semibold text-black dark:text-white">Konfigurasi Kasir (Point of Sale)</h2>
                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Atur pemungutan pajak penjualan serta tampilan kartu menu pada terminal kasir POS.</p>
                </div>

                <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 divide-y divide-black/5 dark:divide-white/5 overflow-hidden">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 gap-3">
                        <div>
                            <p class="text-[15px] font-medium text-black dark:text-white">Pajak Penjualan (PPN / PB1)</p>
                            <p class="text-[13px] text-black/50 dark:text-white/50">Aktifkan pemungutan tarif pajak otomatis pada transaksi kasir POS</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <input type="hidden" name="pos_enable_tax" value="0">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="pos_enable_tax" value="1" {{ $business->pos_enable_tax ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-black/20 peer-focus:outline-none rounded-full peer dark:bg-white/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]"></div>
                            </label>
                            <div class="flex items-center gap-1.5">
                                <input type="number" name="pos_tax_percent" value="{{ $business->pos_tax_percent ?? 0 }}" min="0" max="100" step="0.01"
                                    class="w-20 h-9 text-right bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[8px] px-2.5 text-[13px] font-semibold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                <span class="text-[13px] font-semibold text-black/50 dark:text-white/50">%</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4">
                        <div>
                            <p class="text-[15px] font-medium text-black dark:text-white">Foto Produk pada Menu Kasir</p>
                            <p class="text-[13px] text-black/50 dark:text-white/50">Tampilkan visual foto/thumbnail produk pada kartu katalog POS</p>
                        </div>
                        <input type="hidden" name="pos_show_product_images" value="0">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="pos_show_product_images" value="1" {{ $business->pos_show_product_images ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-black/20 peer-focus:outline-none rounded-full peer dark:bg-white/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#007AFF]"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Section 3: Rekening Bank Resmi -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
                <div class="border-b border-black/5 dark:border-white/5 pb-3">
                    <h2 class="text-[17px] font-semibold text-black dark:text-white">Instruksi Transfer Faktur (Rekening Bank)</h2>
                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Rincian rekening ini otomatis tercetak pada bagian bawah Faktur Penjualan resmi untuk transfer pembayaran pelanggan.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Nama Bank</label>
                        <input type="text" name="bank_name" value="{{ $business->bank_name }}" placeholder="BCA / Mandiri / BNI / BRI"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Nomor Rekening</label>
                        <input type="text" name="bank_account_number" value="{{ $business->bank_account_number }}" placeholder="123-456-7890"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white tabular-nums placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Atas Nama Pemilik Rekening</label>
                        <input type="text" name="bank_account_holder" value="{{ $business->bank_account_holder }}" placeholder="PT Usaha Bersama"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>
            </div>

            <!-- Section 4: Mata Uang & Pembulatan HPP -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
                <div class="border-b border-black/5 dark:border-white/5 pb-3">
                    <h2 class="text-[17px] font-semibold text-black dark:text-white">Mata Uang &amp; Strategi Pembulatan Harga</h2>
                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Strategi pembulatan diterapkan pada HPP per unit final dan Harga Jual akhir produk. Kalkulasi internal tetap mempertahankan presisi desimal floating-point murni.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Kode Mata Uang</label>
                        <input type="text" name="currency_code" value="{{ $business->currency_code }}" required
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white tabular-nums uppercase focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Simbol Mata Uang</label>
                        <input type="text" name="currency_symbol" value="{{ $business->currency_symbol }}" required
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>

                <div class="space-y-2 pt-2">
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70">Aturan Pembulatan Output Akhir</label>
                    <select name="rounding_strategy" required x-model="roundingStrategy"
                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition cursor-pointer">
                        <option value="ROUND">ROUND — Pembulatan Standar Terdekat</option>
                        <option value="CEIL">CEIL — Pembulatan Ke Atas (Plafon)</option>
                        <option value="FLOOR">FLOOR — Pembulatan Ke Bawah</option>
                        <option value="ROUND_50">ROUND_50 — Kelipatan 50 Terdekat</option>
                        <option value="ROUND_100">ROUND_100 — Kelipatan 100 Terdekat (Standar Bisnis UMKM)</option>
                        <option value="ROUND_500">ROUND_500 — Kelipatan 500 Terdekat</option>
                        <option value="ROUND_1000">ROUND_1000 — Kelipatan 1.000 Terdekat</option>
                    </select>

                    <div class="rounded-[10px] bg-[#007AFF]/8 border border-[#007AFF]/15 p-3 text-[13px] text-[#007AFF] font-medium flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                        <span>Simulasi Hasil: <strong class="tabular-nums" x-text="getRoundingExample(roundingStrategy)"></strong></span>
                    </div>
                </div>
            </div>

            <!-- Submit Button Bar -->
            <div class="flex items-center justify-end pt-2">
                <button type="submit"
                    class="h-10 px-6 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-2 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <span>Simpan Profil &amp; Pengaturan Bisnis</span>
                </button>
            </div>
        </form>
    </div>

    <!-- ===================================================== -->
    <!-- TAB 2: 20 PRESET TEMPLATE INDUSTRI (§35)              -->
    <!-- ===================================================== -->
    <div x-show="activeTab === 'templates'" class="space-y-6" style="display: none;">
        <!-- Search & Category Filters -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-[17px] font-semibold text-black dark:text-white flex items-center gap-2">
                        <span>Preset Template Industri Siap Pakai</span>
                    </h2>
                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Terapkan struktur kategori dan komponen biaya default secara instan tanpa mengunci kebebasan kustomisasi mandiri Anda.</p>
                </div>

                <!-- macOS Style Capsule Search -->
                <div class="relative w-full md:w-72">
                    <svg class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input type="text" x-model="templateSearch" placeholder="Cari nama template / industri..."
                        class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-9 pr-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
            </div>

            <!-- Segmented Category Filters -->
            <div class="flex items-center gap-1.5 flex-wrap pt-2 border-t border-black/5 dark:border-white/5">
                <button type="button" @click="selectedCategory = 'all'"
                    :class="selectedCategory === 'all' ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                    class="h-8 px-3 rounded-[8px] text-[12px] font-medium transition-all">
                    Semua Kategori
                </button>
                <button type="button" @click="selectedCategory = 'fnb'"
                    :class="selectedCategory === 'fnb' ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                    class="h-8 px-3 rounded-[8px] text-[12px] font-medium transition-all">
                    Kuliner &amp; F&amp;B
                </button>
                <button type="button" @click="selectedCategory = 'retail'"
                    :class="selectedCategory === 'retail' ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                    class="h-8 px-3 rounded-[8px] text-[12px] font-medium transition-all">
                    Retail &amp; Toko
                </button>
                <button type="button" @click="selectedCategory = 'manufacturing'"
                    :class="selectedCategory === 'manufacturing' ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                    class="h-8 px-3 rounded-[8px] text-[12px] font-medium transition-all">
                    Manufaktur &amp; Produksi
                </button>
                <button type="button" @click="selectedCategory = 'service'"
                    :class="selectedCategory === 'service' ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                    class="h-8 px-3 rounded-[8px] text-[12px] font-medium transition-all">
                    Jasa &amp; Kreatif
                </button>
                <button type="button" @click="selectedCategory = 'agriculture'"
                    :class="selectedCategory === 'agriculture' ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                    class="h-8 px-3 rounded-[8px] text-[12px] font-medium transition-all">
                    Agribisnis &amp; Pertanian
                </button>
            </div>
        </div>

        <!-- Templates Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($templates as $tmpl)
            <div x-show="(selectedCategory === 'all' || '{{ strtolower($tmpl->industry_category) }}'.includes(selectedCategory)) && (!templateSearch || '{{ strtolower($tmpl->name . ' ' . $tmpl->description . ' ' . $tmpl->industry_category) }}'.includes(templateSearch.toLowerCase()))"
                class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 flex flex-col justify-between space-y-4 hover:border-black/15 dark:hover:border-white/15 transition-all">
                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                            {{ $tmpl->industry_category }}
                        </span>
                        <span class="text-[11px] font-semibold text-black/40 dark:text-white/40 tabular-nums">
                            {{ $tmpl->recommended_costing_method }}
                        </span>
                    </div>

                    <h3 class="text-[16px] font-semibold text-black dark:text-white">
                        {{ $tmpl->name }}
                    </h3>
                    <p class="text-[13px] text-black/55 dark:text-white/55 line-clamp-2 leading-relaxed">
                        {{ $tmpl->description }}
                    </p>
                </div>

                <div class="pt-3 border-t border-black/[0.04] dark:divide-white/[0.06] flex items-center justify-between gap-2">
                    <span class="text-[12px] text-black/45 dark:text-white/45 tabular-nums">
                        {{ count($tmpl->default_cost_components ?? []) }} Komponen Biaya
                    </span>

                    <button type="button" @click="openApplyTemplate('{{ $tmpl->code }}', '{{ addslashes($tmpl->name) }}', '{{ addslashes($tmpl->industry_category) }}')"
                        class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        <span>Terapkan</span>
                    </button>

                    <form id="form-apply-template-{{ $tmpl->code }}" method="POST" action="{{ route('settings.apply-template') }}" class="hidden">
                        @csrf
                        <input type="hidden" name="template_code" value="{{ $tmpl->code }}">
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- APPLE ALERT DIALOG (Terapkan Template Industri)       -->
    <!-- ===================================================== -->
    <div x-show="templateModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px]"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-[310px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
            @click.away="closeApplyTemplate()"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="px-5 pt-5 pb-4">
                <p class="text-[17px] font-semibold text-black dark:text-white">Terapkan Template?</p>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1.5 leading-snug">
                    Terapkan preset <strong class="text-black dark:text-white" x-text="selectedTemplate.name"></strong> ke bisnis Anda? Kategori dan akun biaya default akan ditambahkan.
                </p>
            </div>

            <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                <button type="button" @click="closeApplyTemplate()" class="py-3 text-[#007AFF] border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Batal
                </button>
                <button type="button" @click="submitApplyTemplate()" class="py-3 text-[#007AFF] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Terapkan
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
