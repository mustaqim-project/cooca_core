@extends('layouts.app', [
    'title' => 'Pengaturan Bisnis & Template',
    'headerTitle' => 'Pengaturan Bisnis & Template Industri',
    'headerSubtitle' => 'Konfigurasi profil usaha, logo resmi, instruksi transfer faktur, strategi pembulatan HPP, dan preset industri.'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-16" x-data="settingsPage()">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 shadow-sm">
        <div>
            <!-- Breadcrumb (macOS Style) -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <span class="text-black/70 dark:text-white/70 font-medium">Pengaturan</span>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Profil &amp; Template</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Pengaturan Bisnis &amp; Industri</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Profil usaha resmi, format faktur, konfigurasi kasir POS, template WhatsApp, dan preset industri.</p>
        </div>

        <!-- Apple-Style Segmented Controls Switcher -->
        <div class="inline-flex p-0.5 rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] text-[13px] font-medium self-stretch sm:self-auto overflow-x-auto no-scrollbar max-w-full">
            <button type="button" @click="activeTab = 'general'"
                :class="activeTab === 'general' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55 hover:text-black/80 dark:hover:text-white/80'"
                class="px-3.5 py-1.5 rounded-[7px] transition-all flex items-center justify-center gap-2 shrink-0 active:scale-[0.97] active:opacity-80">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.75a1.5 1.5 0 011.5-1.5h3a1.5 1.5 0 011.5 1.5V21" />
                </svg>
                <span>Profil &amp; Pembulatan</span>
            </button>

            <button type="button" @click="activeTab = 'wa_receipt'"
                :class="activeTab === 'wa_receipt' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55 hover:text-black/80 dark:hover:text-white/80'"
                class="px-3.5 py-1.5 rounded-[7px] transition-all flex items-center justify-center gap-2 shrink-0 active:scale-[0.97] active:opacity-80">
                <svg class="w-4 h-4 text-[#34C759]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z"/>
                </svg>
                <span>Template WA Struk</span>
            </button>

            <button type="button" @click="activeTab = 'templates'"
                :class="activeTab === 'templates' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55 hover:text-black/80 dark:hover:text-white/80'"
                class="px-3.5 py-1.5 rounded-[7px] transition-all flex items-center justify-center gap-2 shrink-0 active:scale-[0.97] active:opacity-80">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0l4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0l-5.571 3-5.571-3" />
                </svg>
                <span>Preset Industri</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-black/10 dark:bg-white/10 tabular-nums">{{ $templates->count() }}</span>
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
    @if($errors->any())
        <div class="rounded-[14px] bg-[#FF3B30]/12 border border-[#FF3B30]/20 px-4 py-3 text-[13px] text-[#C41E17] dark:text-[#FF453A] space-y-1">
            <div class="flex items-center gap-2 font-semibold">
                <span class="w-2 h-2 rounded-full bg-[#FF3B30] shrink-0"></span>
                <span>Terdapat kesalahan pada formulir:</span>
            </div>
            <ul class="list-disc list-inside text-[12px] pl-4 space-y-0.5 opacity-90">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- ===================================================== -->
    <!-- TAB 1: PROFIL BISNIS, LOGO, BANK & PEMBULATAN        -->
    <!-- ===================================================== -->
    <div x-show="activeTab === 'general'" class="space-y-6">
        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="_tab" value="general">

            <!-- Section 1: Logo & Identitas Kop Surat -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 shadow-sm">
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
                        <label for="biz_logo_input" class="block text-[13px] font-semibold text-black dark:text-white">Unggah Berkas Logo Baru</label>
                        <input type="file" name="logo" id="biz_logo_input" accept="image/*" @change="handleLogoChange($event)"
                            class="block w-full text-[13px] text-black/60 dark:text-white/60 file:mr-3 file:py-1.5 file:px-3 file:rounded-[8px] file:border-0 file:text-[12px] file:font-semibold file:bg-[#007AFF] file:text-white hover:file:bg-[#0071E3] active:file:scale-[0.97] file:transition file:cursor-pointer cursor-pointer">
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
                        <label for="biz_name_input" class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            Nama Usaha / Perusahaan <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="text" id="biz_name_input" name="name" value="{{ old('name', $business->name) }}" required
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <p class="mt-1 text-[11px] text-black/40 dark:text-white/40">Nama usaha harus unik dan menjadi bagian dari URL website publik.</p>
                    </div>
                    <div>
                        <label for="biz_tax_input" class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            NPWP / Identitas Pajak
                        </label>
                        <input type="text" id="biz_tax_input" name="tax_identification_number" value="{{ old('tax_identification_number', $business->tax_identification_number) }}" placeholder="01.234.567.8-901.000"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white tabular-nums placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>

                <!-- Input Grid: Phone & Email -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="biz_phone_input" class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            No. Telepon / WhatsApp Resmi
                        </label>
                        <input type="text" id="biz_phone_input" name="phone" value="{{ old('phone', $business->phone) }}" placeholder="0812-3456-7890"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white tabular-nums placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label for="biz_email_input" class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            Email Penagihan &amp; Operasional
                        </label>
                        <input type="email" id="biz_email_input" name="email" value="{{ old('email', $business->email) }}" placeholder="finance@perusahaan.com"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>

                <!-- Address -->
                <div>
                    <label for="biz_address_input" class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                        Alamat Kantor / Workshop (Kop Surat)
                    </label>
                    <textarea id="biz_address_input" name="address" rows="2" placeholder="Jl. Sudirman No. 45, Gedung Cyber Lt. 5, Jakarta..."
                        class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-3 text-[15px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition resize-none">{{ old('address', $business->address) }}</textarea>
                </div>
            </div>

            <!-- Section 2: Konfigurasi POS & Pajak -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4 shadow-sm">
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
                            <!-- Apple iOS Toggle Switch -->
                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                <input type="checkbox" name="pos_enable_tax" value="1" {{ $business->pos_enable_tax ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-black/15 peer-focus:outline-none rounded-full peer dark:bg-white/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all after:shadow-sm peer-checked:bg-[#34C759]"></div>
                            </label>
                            <div class="flex items-center gap-1.5">
                                <input type="number" name="pos_tax_percent" value="{{ old('pos_tax_percent', $business->pos_tax_percent ?? 0) }}" min="0" max="100" step="0.01"
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
                        <!-- Apple iOS Toggle Switch -->
                        <label class="relative inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="pos_show_product_images" value="1" {{ $business->pos_show_product_images ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-black/15 peer-focus:outline-none rounded-full peer dark:bg-white/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all after:shadow-sm peer-checked:bg-[#007AFF]"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Section 3: Rekening Bank Resmi -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4 shadow-sm">
                <div class="border-b border-black/5 dark:border-white/5 pb-3">
                    <h2 class="text-[17px] font-semibold text-black dark:text-white">Instruksi Transfer Faktur (Rekening Bank)</h2>
                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Rincian rekening ini otomatis tercetak pada bagian bawah Faktur Penjualan resmi untuk transfer pembayaran pelanggan.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="bank_name_input" class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Nama Bank</label>
                        <input type="text" id="bank_name_input" name="bank_name" value="{{ old('bank_name', $business->bank_name) }}" placeholder="BCA / Mandiri / BNI / BRI"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label for="bank_acc_num_input" class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Nomor Rekening</label>
                        <input type="text" id="bank_acc_num_input" name="bank_account_number" value="{{ old('bank_account_number', $business->bank_account_number) }}" placeholder="123-456-7890"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white tabular-nums placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label for="bank_holder_input" class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Atas Nama Pemilik Rekening</label>
                        <input type="text" id="bank_holder_input" name="bank_account_holder" value="{{ old('bank_account_holder', $business->bank_account_holder) }}" placeholder="PT Usaha Bersama"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>
            </div>

            <!-- Section 4: Mata Uang & Pembulatan HPP -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4 shadow-sm">
                <div class="border-b border-black/5 dark:border-white/5 pb-3">
                    <h2 class="text-[17px] font-semibold text-black dark:text-white">Mata Uang &amp; Strategi Pembulatan Harga</h2>
                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Strategi pembulatan diterapkan pada HPP per unit final dan Harga Jual akhir produk. Kalkulasi internal tetap mempertahankan presisi desimal floating-point murni.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="currency_code_input" class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Kode Mata Uang</label>
                        <input type="text" id="currency_code_input" name="currency_code" value="{{ old('currency_code', $business->currency_code) }}" required
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white tabular-nums uppercase focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label for="currency_symbol_input" class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Simbol Mata Uang</label>
                        <input type="text" id="currency_symbol_input" name="currency_symbol" value="{{ old('currency_symbol', $business->currency_symbol) }}" required
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>

                <div class="space-y-2 pt-2">
                    <label for="rounding_strategy_select" class="block text-[13px] font-medium text-black/70 dark:text-white/70">Aturan Pembulatan Output Akhir</label>
                    <select id="rounding_strategy_select" name="rounding_strategy" required x-model="roundingStrategy"
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
                        <span>Simulasi Hasil: <strong class="tabular-nums font-semibold" x-text="getRoundingExample(roundingStrategy)"></strong></span>
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
    <!-- TAB: CMS TEMPLATE WHATSAPP STRUK                      -->
    <!-- ===================================================== -->
    <div x-show="activeTab === 'wa_receipt'" class="space-y-6" style="display: none;">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Form & Controls (Left Column, 7 cols) -->
            <div class="lg:col-span-7 space-y-6">
                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 shadow-sm">
                    <div class="border-b border-black/5 dark:border-white/5 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-[10px] bg-[#34C759]/12 text-[#34C759] flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.976.58 2.028.928 3.149.929 3.182 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.768-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.073.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.203c.043.072.043.419-.101.824z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-[17px] font-semibold text-black dark:text-white">Kustomisasi Pesan WhatsApp Struk</h2>
                                <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Atur format teks pesan yang menyertai gambar struk saat kasir mengirim ke nomor WhatsApp pelanggan.</p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('settings.update') }}" class="space-y-5">
                        @csrf
                        <input type="hidden" name="_tab" value="wa_receipt">

                        <!-- Dynamic Variable Chips -->
                        <div class="space-y-2">
                            <label class="block text-[13px] font-medium text-black/80 dark:text-white/80">
                                Variabel Dinamis <span class="text-[11px] font-normal text-black/40 dark:text-white/40">(Klik untuk menyisipkan ke kursor teks)</span>
                            </label>
                            <div class="flex flex-wrap gap-1.5">
                                <button type="button" @click="insertTag('{business_name}')"
                                    class="px-2.5 py-1 rounded-full text-[12px] font-medium bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/18 active:scale-[0.97] active:opacity-80 transition flex items-center gap-1 cursor-pointer">
                                    <span>+</span>
                                    <span>{business_name}</span>
                                </button>
                                <button type="button" @click="insertTag('{customer_name}')"
                                    class="px-2.5 py-1 rounded-full text-[12px] font-medium bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/18 active:scale-[0.97] active:opacity-80 transition flex items-center gap-1 cursor-pointer">
                                    <span>+</span>
                                    <span>{customer_name}</span>
                                </button>
                                <button type="button" @click="insertTag('{order_number}')"
                                    class="px-2.5 py-1 rounded-full text-[12px] font-medium bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/18 active:scale-[0.97] active:opacity-80 transition flex items-center gap-1 cursor-pointer">
                                    <span>+</span>
                                    <span>{order_number}</span>
                                </button>
                                <button type="button" @click="insertTag('{date}')"
                                    class="px-2.5 py-1 rounded-full text-[12px] font-medium bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/18 active:scale-[0.97] active:opacity-80 transition flex items-center gap-1 cursor-pointer">
                                    <span>+</span>
                                    <span>{date}</span>
                                </button>
                                <button type="button" @click="insertTag('{cashier_name}')"
                                    class="px-2.5 py-1 rounded-full text-[12px] font-medium bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/18 active:scale-[0.97] active:opacity-80 transition flex items-center gap-1 cursor-pointer">
                                    <span>+</span>
                                    <span>{cashier_name}</span>
                                </button>
                                <button type="button" @click="insertTag('{receipt_link}')"
                                    class="px-2.5 py-1 rounded-full text-[12px] font-medium bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/18 active:scale-[0.97] active:opacity-80 transition flex items-center gap-1 cursor-pointer">
                                    <span>+</span>
                                    <span>{receipt_link}</span>
                                </button>
                                <button type="button" @click="insertTag('{footer_note}')"
                                    class="px-2.5 py-1 rounded-full text-[12px] font-medium bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/18 active:scale-[0.97] active:opacity-80 transition flex items-center gap-1 cursor-pointer">
                                    <span>+</span>
                                    <span>{footer_note}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Template Textarea -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label for="pos_receipt_wa_template" class="block text-[13px] font-medium text-black/80 dark:text-white/80">
                                    Format Teks Pesan WhatsApp
                                </label>
                                <span class="text-[11px] text-black/40 dark:text-white/40 font-mono tabular-nums" x-text="(waTemplateText ? waTemplateText.length : 0) + ' / 1000 karakter'"></span>
                            </div>
                            <textarea
                                id="pos_receipt_wa_template"
                                name="pos_receipt_wa_template"
                                x-ref="waTextarea"
                                x-model="waTemplateText"
                                rows="8"
                                maxlength="1000"
                                class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] p-3.5 text-[14px] font-mono leading-relaxed text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition resize-y"
                                placeholder="Tulis template pesan WhatsApp Anda di sini..."></textarea>
                            <p class="text-[11px] text-black/45 dark:text-white/45">
                                Catatan: Detail rincian pesanan, total belanja, dan QR sudah otomatis tercetak rapi di dalam <strong>gambar file struk PNG</strong>.
                            </p>
                        </div>

                        <!-- Catatan Kaki Tambahan (pos_receipt_footer_note) -->
                        <div class="space-y-1.5 pt-1">
                            <label for="pos_receipt_footer_note" class="block text-[13px] font-medium text-black/80 dark:text-white/80">
                                Catatan Kaki Struk <span class="text-[11px] font-normal text-black/40 dark:text-white/40">({footer_note})</span>
                            </label>
                            <input
                                type="text"
                                id="pos_receipt_footer_note"
                                name="pos_receipt_footer_note"
                                value="{{ old('pos_receipt_footer_note', $business->pos_receipt_footer_note) }}"
                                placeholder="Contoh: Barang yang sudah dibeli tidak dapat ditukar atau dikembalikan."
                                class="w-full h-11 px-3.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[15px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>

                        <!-- Markdown Cheatsheet Guide -->
                        <div class="rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3.5 text-[12px] space-y-1.5">
                            <div class="font-medium text-black/70 dark:text-white/70 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                                </svg>
                                <span>Panduan Format Tulisan WhatsApp:</span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-black/60 dark:text-white/60">
                                <div><code>*teks*</code> &rarr; <strong>Tebal</strong></div>
                                <div><code>_teks_</code> &rarr; <em>Miring</em></div>
                                <div><code>~teks~</code> &rarr; <del>Coret</del></div>
                                <div><code>```teks```</code> &rarr; Monospace</div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-between pt-2 border-t border-black/5 dark:border-white/5">
                            <button type="button" @click="resetToDefaultTemplate()"
                                class="h-10 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition">
                                Reset Template Default
                            </button>
                            <button type="submit"
                                class="h-10 px-6 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition flex items-center gap-2 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                <span>Simpan Template WA</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- WhatsApp Live Phone Preview (Right Column, 5 cols) -->
            <div class="lg:col-span-5">
                <div class="lg:sticky lg:top-6 space-y-3">
                    <div class="flex items-center justify-between px-1">
                        <span class="text-[12px] font-semibold tracking-wider uppercase text-black/40 dark:text-white/40">Pratinjau Langsung (Live Preview)</span>
                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-[#34C759]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] animate-pulse"></span>
                            Sinkron Otomatis
                        </span>
                    </div>

                    <!-- Apple iOS Hardware Phone Frame -->
                    <div class="w-full max-w-[340px] mx-auto rounded-[38px] p-2.5 bg-neutral-900 shadow-[0_20px_60px_rgba(0,0,0,0.35)] border border-neutral-800">
                        <div class="rounded-[28px] overflow-hidden bg-[#EFEAE2] dark:bg-[#0B141A] flex flex-col min-h-[510px] border border-black/10 dark:border-white/10 select-none relative">
                            
                            <!-- Dynamic Island Header -->
                            <div class="bg-[#075E54] dark:bg-[#1F2C34] pt-2 pb-1 flex justify-center">
                                <div class="w-20 h-4 rounded-full bg-black flex items-center justify-end px-2">
                                    <div class="w-1.5 h-1.5 rounded-full bg-neutral-700"></div>
                                </div>
                            </div>

                            <!-- WhatsApp Top Bar -->
                            <div class="bg-[#075E54] dark:bg-[#1F2C34] text-white px-3.5 py-2.5 flex items-center gap-2.5 shadow-sm">
                                <div class="w-8 h-8 rounded-full bg-white/20 text-white font-bold text-[13px] flex items-center justify-center">
                                    {{ strtoupper(substr($business->name ?? 'T', 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-[13px] font-semibold truncate leading-tight text-white">{{ $business->name ?? 'Toko Saya' }}</h4>
                                    <p class="text-[10px] text-white/70 leading-tight">online</p>
                                </div>
                                <div class="flex items-center gap-3 text-white/80">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
                                </div>
                            </div>

                            <!-- WhatsApp Chat Body Area -->
                            <div class="flex-1 p-3 flex flex-col justify-end space-y-2 overflow-y-auto">
                                
                                <!-- Chat Bubble (Outgoing) -->
                                <div class="bg-[#D9FDD3] dark:bg-[#005C4B] text-[#111B21] dark:text-[#E9EDEF] rounded-[12px] rounded-tr-none p-2 shadow-sm max-w-[95%] ml-auto text-[12.5px] leading-relaxed space-y-2">
                                    
                                    <!-- Simulated Receipt Image Thumbnail -->
                                    <div class="rounded-[8px] overflow-hidden bg-white dark:bg-[#111B21] border border-black/5 dark:border-white/10 p-3 shadow-sm text-center space-y-1.5">
                                        <div class="flex items-center justify-center gap-1 text-[11px] font-semibold text-black/70 dark:text-white/70">
                                            <span>🧾</span>
                                            <span>STRUK DIGITAL</span>
                                        </div>
                                        <div class="h-[1px] w-full border-t border-dashed border-black/20 dark:border-white/20"></div>
                                        <div class="text-[11px] font-bold text-black/80 dark:text-white/80">{{ $business->name ?? 'Toko Saya' }}</div>
                                        <div class="text-[9.5px] text-black/50 dark:text-white/50 font-mono tabular-nums">No: POS-20260910-0042</div>
                                        <div class="py-1">
                                            <span class="inline-block px-2 py-0.5 rounded bg-black/5 dark:bg-white/10 text-[10px] font-semibold text-black/70 dark:text-white/70">
                                                [File Gambar Struk PNG]
                                            </span>
                                        </div>
                                        <div class="h-[1px] w-full border-t border-dashed border-black/20 dark:border-white/20"></div>
                                        <div class="text-[9.5px] text-[#248A3D] dark:text-[#30D158] font-semibold tabular-nums">LUNAS • Rp 85.000</div>
                                    </div>

                                    <!-- Rendered WhatsApp Text Caption -->
                                    <div class="whitespace-pre-wrap select-text text-[12px] leading-relaxed break-words pt-1" x-html="formatWaMarkdown(waRenderedPreview)"></div>

                                    <!-- Time & Read Status -->
                                    <div class="flex items-center justify-end gap-1 text-[10px] text-black/45 dark:text-white/50 select-none">
                                        <span class="tabular-nums">{{ now()->format('H:i') }}</span>
                                        <span class="text-[#53bdeb] font-bold text-[11px]">✓✓</span>
                                    </div>
                                </div>

                            </div>

                            <!-- Fake Message Input Bar -->
                            <div class="bg-[#F0F2F5] dark:bg-[#1F2C34] p-2 flex items-center gap-2 border-t border-black/5 dark:border-white/5">
                                <div class="w-6 h-6 rounded-full bg-black/10 dark:bg-white/10 flex items-center justify-center text-black/50 dark:text-white/50 text-[11px] font-bold">+</div>
                                <div class="flex-1 h-7 rounded-full bg-white dark:bg-[#2A3942] px-3 flex items-center text-[11px] text-black/40 dark:text-white/40">Ketik pesan</div>
                                <div class="w-6 h-6 rounded-full bg-[#00A884] text-white flex items-center justify-center text-[11px]">▶</div>
                            </div>

                        </div>
                    </div>

                    <!-- Informational Callout -->
                    <div class="rounded-[12px] bg-[#007AFF]/8 border border-[#007AFF]/15 p-3 text-[12px] text-black/60 dark:text-white/60 space-y-1">
                        <p class="font-medium text-[#007AFF] flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.002 6.002 0 00-4-5.659V5a2 2 0 114 0v2.091a6.002 6.002 0 004 5.659V18m-4 0v1.5a1.5 1.5 0 01-3 0V18m3 0h-3"/>
                            </svg>
                            <span>Informasi Pengiriman</span>
                        </p>
                        <p>Format pesan di atas dikirim otomatis sebagai <strong>caption gambar struk</strong> ketika kasir menekan tombol kirim WhatsApp di POS atau melalui WhatsApp Gateway bot.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- TAB 2: 20 PRESET TEMPLATE INDUSTRI (§35)              -->
    <!-- ===================================================== -->
    <div x-show="activeTab === 'templates'" class="space-y-6" style="display: none;">
        <!-- Search & Category Filters -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 space-y-4 shadow-sm">
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
                    class="h-8 px-3 rounded-[8px] text-[12px] font-medium active:scale-[0.97] active:opacity-80 transition-all">
                    Semua Kategori
                </button>
                <button type="button" @click="selectedCategory = 'fnb'"
                    :class="selectedCategory === 'fnb' ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                    class="h-8 px-3 rounded-[8px] text-[12px] font-medium active:scale-[0.97] active:opacity-80 transition-all">
                    Kuliner &amp; F&amp;B
                </button>
                <button type="button" @click="selectedCategory = 'retail'"
                    :class="selectedCategory === 'retail' ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                    class="h-8 px-3 rounded-[8px] text-[12px] font-medium active:scale-[0.97] active:opacity-80 transition-all">
                    Retail &amp; Toko
                </button>
                <button type="button" @click="selectedCategory = 'manufacturing'"
                    :class="selectedCategory === 'manufacturing' ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                    class="h-8 px-3 rounded-[8px] text-[12px] font-medium active:scale-[0.97] active:opacity-80 transition-all">
                    Manufaktur &amp; Produksi
                </button>
                <button type="button" @click="selectedCategory = 'service'"
                    :class="selectedCategory === 'service' ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                    class="h-8 px-3 rounded-[8px] text-[12px] font-medium active:scale-[0.97] active:opacity-80 transition-all">
                    Jasa &amp; Kreatif
                </button>
                <button type="button" @click="selectedCategory = 'agriculture'"
                    :class="selectedCategory === 'agriculture' ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                    class="h-8 px-3 rounded-[8px] text-[12px] font-medium active:scale-[0.97] active:opacity-80 transition-all">
                    Agribisnis &amp; Pertanian
                </button>
            </div>
        </div>

        <!-- Templates Grid (Dense & Structured) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($templates as $tmpl)
            <div x-show="(selectedCategory === 'all' || '{{ strtolower($tmpl->industry_category) }}'.includes(selectedCategory)) && (!templateSearch || '{{ strtolower($tmpl->name . ' ' . $tmpl->description . ' ' . $tmpl->industry_category) }}'.includes(templateSearch.toLowerCase()))"
                class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 flex flex-col justify-between space-y-4 hover:border-black/15 dark:hover:border-white/15 transition-all duration-200 shadow-sm">
                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                            {{ $tmpl->industry_category }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-medium bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 tabular-nums">
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

                <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between gap-2">
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

<script>
function settingsPage() {
    return {
        activeTab: @json(session('active_tab', 'general')),
        templateSearch: '',
        selectedCategory: 'all',
        logoPreview: @json($business->logo_url),
        roundingStrategy: @json($business->rounding_strategy ?? 'ROUND_100'),
        templateModalOpen: false,
        selectedTemplate: { code: '', name: '', category: '' },

        // WhatsApp Receipt CMS state
        waTemplateText: @json($business->pos_receipt_wa_template ?: "🧾 *STRUK PEMBELIAN*\n*{business_name}*\n\nHalo Kak *{customer_name}*! 🙏\nTerima kasih banyak telah berbelanja di *{business_name}*.\n\nTerlampir gambar struk digital untuk transaksi Anda.\n\nSemoga hari Anda menyenangkan! ✨"),

        insertTag(tag) {
            const el = this.$refs.waTextarea;
            if (!el) {
                this.waTemplateText += tag;
                return;
            }
            const start = el.selectionStart || 0;
            const end = el.selectionEnd || 0;
            const current = this.waTemplateText || '';
            this.waTemplateText = current.substring(0, start) + tag + current.substring(end);
            this.$nextTick(() => {
                el.focus();
                el.setSelectionRange(start + tag.length, start + tag.length);
            });
        },

        resetToDefaultTemplate() {
            this.waTemplateText = "🧾 *STRUK PEMBELIAN*\n*{business_name}*\n\nHalo Kak *{customer_name}*! 🙏\nTerima kasih banyak telah berbelanja di *{business_name}*.\n\nTerlampir gambar struk digital untuk transaksi Anda.\n\nSemoga hari Anda menyenangkan! ✨";
        },

        get waRenderedPreview() {
            let text = this.waTemplateText || '';
            const replacements = {
                '{business_name}': @json($business->name),
                '{customer_name}': 'Agung Mustaqim',
                '{order_number}': 'POS-20260910-0042',
                '{date}': @json(now()->format('d/m/Y H:i')),
                '{cashier_name}': @json(auth()->user()?->name ?? 'Kasir'),
                '{receipt_link}': 'https://umkm.cooca.id/receipt/sample',
                '{footer_note}': @json($business->pos_receipt_footer_note ?? 'Simpan struk ini sebagai bukti pembayaran sah.')
            };
            for (const [key, val] of Object.entries(replacements)) {
                text = text.replaceAll(key, val || '');
            }
            return text;
        },

        formatWaMarkdown(str) {
            if (!str) return '';
            let escaped = str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
            escaped = escaped.replace(/\*([^\*]+)\*/g, '<strong class="font-bold text-black dark:text-white">$1</strong>');
            escaped = escaped.replace(/_([^_]+)_/g, '<em class="italic">$1</em>');
            escaped = escaped.replace(/~([^~]+)~/g, '<del class="line-through opacity-60">$1</del>');
            escaped = escaped.replace(/```([^`]+)```/g, '<code class="bg-black/10 dark:bg-white/10 px-1 py-0.5 rounded font-mono text-[11px]">$1</code>');
            return escaped.replace(/\n/g, '<br>');
        },

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
    };
}
</script>
@endsection
